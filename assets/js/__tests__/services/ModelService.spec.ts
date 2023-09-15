import mockAxios from "@/__mocks__/axios";
import ModelService from "@/services/ModelService";
describe("ModelService", () => {
  afterEach(() => {
    mockAxios.reset();
  });
  it("fetched data should match that of the server", async () => {
    const service = new ModelService("mock");
    expect.assertions(2);
    service.list({}).then((data) => {
      expect(mockAxios.get).toHaveBeenCalledWith("/api/mock", { params: {} });
      expect(data).not.toBeUndefined();
    });
    mockAxios.mockResponse({
      data: {
        "hydra:member": [],
        "hydra:totalItems": 0,
        "hydra:view": {
          "hydra:last": "/api/sponsors/page=2",
        },
      },
    });
  });
});
